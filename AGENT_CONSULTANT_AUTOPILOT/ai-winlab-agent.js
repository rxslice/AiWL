const AI_WINLAB_META_PROMPT = `You are AI WinLab, an expert business consultant specializing in AI implementation analysis.

## Expertise Profile
- Business process optimization specialist with 15+ years of experience
- AI integration strategist for SMBs and enterprises
- Workflow efficiency expert with focus on automation
- Revenue growth consultant through technology optimization
- Website conversion optimization specialist

## Analysis Framework
1. Business Model Assessment
   - Revenue streams identification
   - Customer journey mapping
   - Core value proposition analysis
   - Market positioning evaluation
   
2. Process Workflow Analysis
   - Operational bottlenecks identification
   - Manual process mapping
   - Communication flow tracking
   - Decision point optimization potential
   
3. Website & Digital Presence Evaluation
   - User experience assessment
   - Content-to-conversion pipeline
   - Customer support touchpoints
   - Digital marketing automation potential
   
4. AI Opportunity Identification
   - Customer interaction automation points
   - Data processing optimization opportunities
   - Decision support system potential
   - Predictive analytics implementation possibilities
   - Content generation and management automation
   
5. Implementation Roadmap Creation
   - Quick wins identification (1-2 week implementation)
   - Medium-term strategies (1-3 month implementation)
   - Long-term transformational opportunities
   - ROI calculation methodology

## Response Format
- Begin with an Executive Summary (250 words maximum)
- Present findings in clear sections with visual support recommendations
- For each identified opportunity:
  * Current state description
  * Pain point articulation
  * AI solution recommendation with specific tools
  * Implementation difficulty (1-5 scale)
  * Expected ROI timeline
  * Measurable KPIs for success tracking
- Conclude with prioritized implementation roadmap

Your analysis must be specific, actionable, and tailored to the exact business case presented. Avoid generic recommendations. Focus on practical AI implementations that can deliver measurable business value within realistic timeframes and budgets.`;

class AIWinLabAnalysisEngine {
    constructor() {
        this.businessData = {};
        this.apiKey = process.env.GEMINI_API_KEY;
        this.apiEndpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent";
    }
    
    async analyzeBusinessData(data) {
        this.businessData = data;
        
        // Prepare the analysis prompt with the business data
        const analysisPrompt = this.prepareAnalysisPrompt();
        
        try {
            // Make API call to Gemini 2.0 Flash
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.apiKey}`
                },
                body: JSON.stringify({
                    contents: [{
                        parts: [{
                            text: analysisPrompt
                        }]
                    }],
                    generationConfig: {
                        temperature: 0.2,
                        topK: 40,
                        topP: 0.95,
                        maxOutputTokens: 8192
                    }
                })
            });
            
            const result = await response.json();
            return this.processAnalysisResult(result);
        } catch (error) {
            console.error("AI Analysis failed:", error);
            return {
                success: false,
                error: "Analysis failed due to API issues. Please try again."
            };
        }
    }
    
    prepareAnalysisPrompt() {
        return `${AI_WINLAB_META_PROMPT}
        
## Business Information for Analysis
- Business Name: ${this.businessData.businessName}
- Industry: ${this.businessData.industry}
- Website URL: ${this.businessData.websiteUrl}
- Company Size: ${this.businessData.companySize}
- Primary Revenue Model: ${this.businessData.revenueModel}

## Current Business Processes
${this.businessData.businessProcesses}

## Sales Process
${this.businessData.salesProcess}

## Current Pain Points
${this.businessData.painPoints}

## Technology Currently In Use
${this.businessData.currentTechnology}

Analyze this business based on the framework above and provide a complete, actionable report with specific AI implementation recommendations. Include suggestions for data visualization formats (charts, graphs, tables) that would best represent each insight.`;
    }
    
    processAnalysisResult(apiResult) {
        // Extract the response text from the API result
        const rawAnalysis = apiResult.candidates[0].content.parts[0].text;
        
        // Parse the sections into structured data for frontend rendering
        const structuredAnalysis = this.parseAnalysisSections(rawAnalysis);
        
        return {
            success: true,
            rawAnalysis,
            structuredAnalysis,
            businessName: this.businessData.businessName,
            generatedDate: new Date().toISOString()
        };
    }
    
    parseAnalysisSections(rawText) {
        // Logic to parse the raw text into structured sections
        // This would identify executive summary, opportunity areas, recommendations, etc.
        // And format them for easy rendering in the frontend
        
        // Simplified version - would be more robust in production
        const sections = rawText.split(/#{2,3}\s+/g).filter(s => s.trim().length > 0);
        
        const structured = {
            executiveSummary: sections[0],
            analysisAreas: [],
            recommendations: [],
            implementationRoadmap: {}
        };
        
        // Further parsing logic would go here
        
        return structured;
    }
}

// Export the engine for use in WordPress
window.AIWinLab = {
    AnalysisEngine: AIWinLabAnalysisEngine
};